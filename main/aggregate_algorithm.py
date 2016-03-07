import pymysql
import math

#TODO: Get blank classification number(s) from database (options) rather than using hard coded value(s) (86+87)

def aggregate_classifications(photo_ID,connection):
    c=connection.cursor()

    sql="SELECT * FROM animal WHERE photo_id="+str(photo_ID)
    c.execute(sql)
    result=c.fetchall()
    
    speciesTally=dict()
    ageTally=dict()
    genderTally=dict()
    numClass=0
    nonBlanks=0
    for row in result:
        rowS=row['species']
        rowA=row['age']
        rowG=row['gender']
        numClass+=1
        if rowS!=86 and rowS!=87:
            nonBlanks+=1
        
        if  rowS in speciesTally:
            speciesTally[rowS]=speciesTally[rowS]+1
        else:
            speciesTally[rowS]=1

        if  rowA in ageTally:
            ageTally[rowS]=ageTally[rowS]+1
        else:
            ageTally[rowS]=1

        if  rowG in genderTally:
            genderTally[rowS]=genderTally[rowS]+1
        else:
            genderTally[rowS]=1

    if numClass>0:
        #Gender and age handled vey basically, just select the mode
        age=ageTally.keys()[ageTally.values().index(max(ageTally.values()))]
        gender=genderTally.keys()[genderTally.values().index(max(genderTally.values()))]
        values=list(speciesTally.values())
        keys=list(speciesTally.keys())
        
        species=keys[values.index(max(values))]

        #According to paper this is correct, strange that blanks are counted?
        support=speciesTally[species]/numClass
        
        blanks=(numClass-nonBlanks)/numClass

        if blanks==1.0:
            evenness=-1.0
        elif support==1.0:
            evenness=0.0
        else:
            pTot=0
            for s in speciesTally:
                if s!=86 and s!=87:
                    p=speciesTally[s]/(nonBlanks)
                    pTot-=p*math.log(p)
            evenness=pTot/math.log(len(values))
    else:
        #Would need to add an unclassified option to database
        #Kinda nice to have all fields here to more easily see everything that needs doing
        species=-1
        evenness=-1.0
        blanks=-1
        support=-1
        age=-1
        gender=-1

    #Calculate flag
    #Values TODO: put in options table
    #   -1=error
    #   0=incomplete
    #   1=blank
    #   2=consensus
    #   3=complete

    #Get flag option_ids from options
    sql="SELECT * FROM options WHERE struc='flag'"
    flagResult=c.fetchall()
    flags=dict()
    for row in flagResult:
        flags[row['option_name']]=row['option_id']

    #If this doesn't get set, should be an error
    flag=-1
    
    #Ten blanks=blank
    if numClass-nonBlanks>=10:
        flag=flags['blank']
    #5 Blanks, no other results=blank
    elif numClass-nonBlanks>=5 and len(speciesTally)==1:
        flag=flags['blank']
    #Ten matching classifications=consensus
    else:
        if species!=-1:
            if speciesTally[species]>=10:
                flag=flags['consensus']
        #No consensus but lots of classifications=complete
        elif numClass>=25:
            flag=flags['complete']
        #Otherwise not done=incomplete
        else:
            flag=flags['incomplete']

    #Check if aggregate already exists, if not insert, otherwise update
    sql="SELECT * FROM aggregate WHERE photo_id="+str(photo_ID)
    c.execute(sql)

    currentAggregate=c.fetchone()
    
    if currentAggregate==None:
        sql="INSERT INTO aggregate VALUES ('{}','{}','{}','{}','{}','{}','{}', '{}', '{}');".format(photo_ID,numClass,species,evenness,blanks,support,flag,age,gender)
    else:
        sql=("UPDATE aggregate SET photo_id='{}',numClass='{}',species='{}',evenness='{}',blanks='{}',support='{}',flag='{}',age='{}',gender='{}' WHERE photo_id="+str(photo_ID)).format(photo_ID,numClass,species,evenness,blanks,support,flag,age,gender)

    c.execute(sql)
    connection.commit()

connection = pymysql.connect(host='localhost',user='root',password='toot',db='mammalwebdump',charset='utf8mb4',cursorclass=pymysql.cursors.DictCursor)
with connection.cursor() as cursor:
    sql="SELECT photo_id FROM `animal` ORDER BY photo_id DESC;"
    cursor.execute(sql)
    maxID=cursor.fetchone()['photo_id']

for id in range(maxID):
    if id%100==0:
        print(id)
    aggregate_classifications(id,connection)

connection.close()
